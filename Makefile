app_name=oidc_groups_mapping
build_dir=build

npm-init:
	npm ci

npm-build:
	npm run build

appstore: npm-init npm-build
	rm -rf $(build_dir)
	mkdir -p $(build_dir)/artifacts
	rsync -a --exclude-from=.nextcloudignore . $(build_dir)/artifacts/$(app_name)
	cd $(build_dir)/artifacts && tar czf $(app_name).tar.gz $(app_name)
	rm -rf $(build_dir)/artifacts/$(app_name)
	@echo "Tarball: $(build_dir)/artifacts/$(app_name).tar.gz"

clean:
	rm -rf $(build_dir)

.PHONY: appstore clean npm-init npm-build
