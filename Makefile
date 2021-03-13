ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

.PHONY: install
install:
	@composer install
	@ln -sfn "${ROOT_DIR}/bin/awssh" /usr/local/bin/awssh
	@ln -sfn "${ROOT_DIR}/bin/awsdbt" /usr/local/bin/awsdbt
	@echo Installed successfully.