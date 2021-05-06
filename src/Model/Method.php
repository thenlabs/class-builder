<?php
declare(strict_types=1);

namespace ThenLabs\ClassBuilder\Model;

use ReflectionFunction;
use Closure;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Method extends AbstractClassMember
{
    use CommentTrait;
    use AccessTrait;
    use AbstractTrait;
    use StaticTrait;

    protected $closure;

    protected $returnType;

    public function setClosure(Closure $closure): self
    {
        $this->closure = $closure;

        return $this;
    }

    public function getClosure(): Closure
    {
        return $this->closure;
    }

    public function getReturnType(): ?string
    {
        return $this->returnType;
    }

    public function setReturnType(?string $returnType): self
    {
        $this->returnType = $returnType;

        return $this;
    }

    public function getCode(): string
    {
        $comments = $this->comments ? $this->comments : '';
        if (! $comments) {
            $comments = "/**\n";
            foreach ($this->commentLines as $comment) {
                $comments .= " * {$comment}\n";
            }
            $comments .= " */";
        }

        $returnType = null;
        $returnTypeStr = $this->returnType ? ": {$this->returnType}" : '';

        $parameters = [];
        $parametersStr = [];

        if ($this->closure instanceof Closure) {
            $reflectionFunction = new ReflectionFunction($this->closure);

            if (! $returnTypeStr) {
                $returnType = $reflectionFunction->getReturnType();
                if ($returnType) {
                    $returnTypeStr = strval($returnType);

                    if (! $returnType->isBuiltin() && $returnTypeStr != 'self') {
                        $returnTypeStr = '\\' . $returnTypeStr;
                    }

                    if ($returnType->allowsNull()) {
                        $returnTypeStr = ': ?' . $returnTypeStr;
                    } else {
                        $returnTypeStr = ': ' . $returnTypeStr;
                    }
                }
            }

            foreach ($reflectionFunction->getParameters() as $parameter) {
                $parameterName = $parameter->getName();
                $parameters[] = $parameterName;

                $refSymbol = $parameter->isPassedByReference() ? '&' : null;
                $paramStr = "{$refSymbol}\${$parameterName}";

                if ($parameter->isVariadic()) {
                    $paramStr = "...{$paramStr}";
                }

                if ($parameter->hasType()) {
                    $type = $parameter->getType();

                    $typeName = (string) $type;
                    if (! $type->isBuiltin()) {
                        $typeName = '\\' . $typeName;
                    }

                    $typeStr = $type->allowsNull() ? '?'.$typeName : $typeName;
                    $paramStr = $typeStr . ' ' . $paramStr;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $paramStr .= ' = ' . var_export($parameter->getDefaultValue(), true);
                }

                $parametersStr[] = $paramStr;
            }
        }

        $parametersStr = implode(',', $parametersStr);

        $abstract = '';
        $static = $this->static ? 'static' : '';

        if ($this->abstract) {
            $abstract = 'abstract';
            $body = ';';
        } else {
            $classBuilder = $this->end();
            $fcqn = $classBuilder->getFCQN();

            $callParamStr = [];
            foreach ($parameters as $parameterName) {
                $callParamStr[] = "\${$parameterName}";
            }
            $callParamStr = implode(',', $callParamStr);
            if ($callParamStr) {
                $callParamStr = ','.$callParamStr;
            }

            $return = 'return';
            if ($returnType instanceof \ReflectionType &&
                strval($returnType) == 'void'
            ) {
                $return = '';
            }

            $closureStr = "\$closure = \\ThenLabs\\ClassBuilder\\ClassBuilder::getInstalledInstances()['{$fcqn}']->getMethod('{$this->name}')->getClosure();\n";
            $body = "{
                {$closureStr}
                {$return} \$closure->call(\$this {$callParamStr});
            }";

            if ($this->static) {
                $closureStr .= "\$closure = \Closure::bind(\$closure, null, self::class);\n";

                $body = "{
                    {$closureStr}
                    {$return} call_user_func(\$closure {$callParamStr});
                }";
            }
        }

        return "
            {$comments}
            {$abstract} {$this->access} {$static} function {$this->name}({$parametersStr}) {$returnTypeStr}
            {$body}
        ";
    }
}
