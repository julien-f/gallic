PROJECT := gallic
VERSION := 0.2

SRC_DIR   ?= src
TESTS_DIR ?= tests

################################################################################

PHPDOC  ?= phpdoc --sourcecode
PHPUNIT ?= phpunit --colors

################################################################################

.DEFAULT_GOAL: all
.PHONY: all codecoverage distcheck
.SILENT:

all:
	echo 'Nothing to compile!'
	echo
	echo 'Available commands:'
	echo '- codecoverage'
	echo '- distcheck'

codecoverage:
	$(PHPUNIT) --bootstrap $(TESTS_DIR)/bootstrap.php --coverage-text $(TESTS_DIR)

distcheck:
	$(PHPUNIT) --bootstrap $(TESTS_DIR)/bootstrap.php --verbose -- $(TESTS_DIR)
