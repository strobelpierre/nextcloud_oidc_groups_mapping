app_name=oidc_groups_mapping
build_dir=build

appstore:
	rm -rf $(build_dir)
	mkdir -p $(build_dir)
	rsync -a --exclude-from=.nextcloudignore . $(build_dir)/$(app_name)
	cd $(build_dir) && tar czf $(app_name).tar.gz $(app_name)
	rm -rf $(build_dir)/$(app_name)
	@echo "Tarball: $(build_dir)/$(app_name).tar.gz"

clean:
	rm -rf $(build_dir)

.PHONY: appstore clean
