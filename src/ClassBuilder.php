<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder;

use Closure;
use ThenLabs\ClassBuilder\Model\AbstractClassMember;
use ThenLabs\ClassBuilder\Model\AbstractTrait;
use ThenLabs\ClassBuilder\Model\CommentTrait;
use ThenLabs\ClassBuilder\Model\Constant;
use ThenLabs\ClassBuilder\Model\Method;
use ThenLabs\ClassBuilder\Model\Property;
use ThenLabs\ClassBuilder\Model\TraitMember;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class ClassBuilder
{
    public const ENTITY_CLASS     = 'class';
    public const ENTITY_TRAIT     = 'trait';
    public const ENTITY_INTERFACE = 'interface';

    use CommentTrait;
    use AbstractTrait;

    protected $name;
    protected $namespace;
    protected $parentClass;
    protected $interfaces = [];
    protected $final = false;
    protected $members = [];
    protected $entityType = 'class';
    protected static $installedInstances = [];

    public function __construct(?string $name = null)
    {
        if (! $name) {
            $name = 'DynamicClass' . uniqid();
        }

        $this->setName($name);
    }

    public static function getInstalledInstances(): array
    {
        return self::$installedInstances;
    }

    public function getFCQN(): string
    {
        $separator = $this->namespace ? '\\' : '';

        return $this->namespace . $separator . $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        if (! Helpers::validNameForClassMember($name)) {
            throw new Exception\InvalidClassNameException($name);
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @param  string|string[] $trait
     * @param  string[]        $definitions
     * @return self
     */
    public function use($traits, array $definitions = []): self
    {
        $checkTrait = function (string $trait) {
            if (! trait_exists($trait)) {
                throw new Exception\UnexistentTraitException($trait);
            }
        };

        if (is_string($traits)) {
            $checkTrait($traits);
        } elseif (is_array($traits)) {
            foreach ($traits as $t) {
                $checkTrait($t);
            }
        } else {
            throw new \TypeError;
        }

        $traitMember = new TraitMember(uniqid('trait'));
        $traitMember->setTraits($traits);
        $traitMember->setDefinitions($definitions);

        $this->members[] = $traitMember;

        return $this;
    }

    public function isInstalled(): bool
    {
        $builder = self::$installedInstances[$this->getFCQN()] ?? null;

        return $this === $builder ? true : false;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $fragments = explode('\\', $namespace);
        foreach ($fragments as $fragment) {
            if (! Helpers::validNameForClassMember($fragment)) {
                throw new Exception\InvalidNamespaceException($namespace);
            }
        }

        $this->namespace = $namespace;

        return $this;
    }

    public function extends(string $parentClass): self
    {
        if (! class_exists($parentClass)) {
            throw new Exception\UnexistentClassException($parentClass);
        }

        $this->parentClass = $parentClass;

        return $this;
    }

    public function getParentClass(): string
    {
        return $this->parentClass;
    }

    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function implements(string ...$interfaces): self
    {
        foreach ($interfaces as $interface) {
            $this->addInterface($interface);
        }

        return $this;
    }

    public function addInterface(string $interfaceName): self
    {
        if (! interface_exists($interfaceName)) {
            throw new Exception\UnexistentInterfaceException($interfaceName);
        }

        $this->interfaces[] = $interfaceName;

        return $this;
    }

    public function setFinal(bool $final): self
    {
        $this->final = $final;

        return $this;
    }

    public function addProperty(string $name): Property
    {
        $property = new Property($name);
        $property->setClassBuilder($this);

        $this->addMember($property);

        return $property;
    }

    public function getProperty(string $name): ?Property
    {
        foreach ($this->members as $member) {
            if ($member instanceof Property &&
                $member->getName() === $name
            ) {
                return $member;
            }
        }

        return null;
    }

    public function removeProperty(string $name): void
    {
        foreach ($this->members as $key => $member) {
            if ($member instanceof Property &&
                $member->getName() === $name
            ) {
                unset($this->members[$key]);
            }
        }
    }

    public function addConstant(string $name): Constant
    {
        $constant = new Constant($name);
        $constant->setClassBuilder($this);

        $this->addMember($constant);

        return $constant;
    }

    public function getConstant(string $name): ?Constant
    {
        foreach ($this->members as $member) {
            if ($member instanceof Constant &&
                $member->getName() === $name
            ) {
                return $member;
            }
        }

        return null;
    }

    public function removeConstant(string $name): void
    {
        foreach ($this->members as $key => $member) {
            if ($member instanceof Constant &&
                $member->getName() === $name
            ) {
                unset($this->members[$key]);
            }
        }
    }

    public function addMember(AbstractClassMember $member): void
    {
        $this->members[] = $member;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function addMethod(string $name, ?Closure $closure = null): Method
    {
        $method = new Method($name);
        $method->setClassBuilder($this);

        if ($closure) {
            $method->setClosure($closure);
        }

        $this->addMember($method);

        return $method;
    }

    public function getMethod(string $name): ?Method
    {
        foreach ($this->members as $member) {
            if ($member instanceof Method &&
                $member->getName() === $name
            ) {
                return $member;
            }
        }

        return null;
    }

    public function removeMethod(string $name): void
    {
        foreach ($this->members as $key => $member) {
            if ($member instanceof Method &&
                $member->getName() === $name
            ) {
                unset($this->members[$key]);
            }
        }
    }

    public function __toString()
    {
        return $this->getFCQN();
    }

    public function install(): self
    {
        $fcqn = $this->getFCQN();
        if (class_exists($fcqn)) {
            throw new Exception\ExistentClassException($fcqn);
        }

        eval($this->getCode());

        self::$installedInstances[$fcqn] = $this;

        return $this;
    }

    public function newInstance(...$args): object
    {
        $fcqn = $this->getFCQN();

        if (! class_exists($fcqn)) {
            $this->install();
        }

        $reflectionClass = new \ReflectionClass($fcqn);

        return $reflectionClass->newInstanceArgs($args);
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): self
    {
        $validTypes = [self::ENTITY_CLASS, self::ENTITY_TRAIT, self::ENTITY_INTERFACE];

        if (! in_array($entityType, $validTypes)) {
            throw new Exception\InvalidEntityTypeException($entityType);
        }

        $this->entityType = $entityType;

        return $this;
    }

    public function getCode(): string
    {
        $namespace = $this->namespace;
        if ($namespace) {
            $namespace = "namespace {$namespace};";
        }

        $comments = $this->comments ? $this->comments : '';
        if (! $comments) {
            $comments = "/**\n";
            foreach ($this->commentLines as $comment) {
                $comments .= " * {$comment}\n";
            }
            $comments .= " */";
        }

        $final = $this->final ? 'final': '';
        $abstract = $this->abstract ? 'abstract': '';

        $extends = '';
        if ($this->parentClass) {
            $extends = "extends \\{$this->parentClass}";
        }

        $interfaces = [];
        foreach ($this->interfaces as $interface) {
            $interfaces[] = '\\'.$interface;
        }
        $implements = '';
        if (! empty($interfaces)) {
            $implements = 'implements ' . implode(',', $interfaces);
        }

        $members = '';
        foreach ($this->members as $member) {
            $members .= $member->getCode();
        }

        $code = "
            {$namespace}

            {$comments}
            {$final} {$abstract} {$this->entityType} {$this->name} {$extends} {$implements}
            {
                {$members}
            }
        ";

        return $code;
    }
}
