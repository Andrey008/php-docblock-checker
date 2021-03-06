<?php

namespace PhpDocBlockChecker\Check;

use PhpDocBlockChecker\FileInfo;
use PhpDocBlockChecker\Status\StatusType\Warning\ParamMismatchWarning;
use PhpDocBlockChecker\Status\StatusType\Warning\ParamMissingWarning;

class ParamCheck extends Check
{

    /**
     * @param FileInfo $file
     */
    public function check(FileInfo $file)
    {
        foreach ($file->getMethods() as $name => $method) {
            // If the docblock is inherited, we can't check for params and return types:
            if (isset($method['docblock']['inherit']) && $method['docblock']['inherit']) {
                continue;
            }

            foreach ($method['params'] as $param => $type) {
                if (!isset($method['docblock']['params'][$param])) {
                    $this->fileStatus->add(
                        new ParamMissingWarning($file->getFileName(), $name, $method['line'], $name, $param)
                    );
                    continue;
                }

                if (!empty($type)) {
                    $docBlockTypes = explode('|', $method['docblock']['params'][$param]);
                    $methodTypes = explode('|', $type);

                    sort($docBlockTypes);
                    sort($methodTypes);

                    if ($docBlockTypes !== $methodTypes) {
                        if ($type === 'array' && substr($method['docblock']['params'][$param], -2) === '[]') {
                            // Do nothing because this is fine.
                        } else {
                            $this->fileStatus->add(
                                new ParamMismatchWarning(
                                    $file->getFileName(),
                                    $name,
                                    $method['line'],
                                    $name,
                                    $param,
                                    $type,
                                    $method['docblock']['params'][$param]
                                )
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function enabled()
    {
        return !$this->config->isSkipSignatures();
    }
}
