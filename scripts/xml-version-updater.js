// SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

const re = /<version>(\d+\.\d+\.\d+)<\/version>/;

module.exports.readVersion = (contents) => contents.match(re)?.[1];
module.exports.writeVersion = (contents, version) => contents.replace(re, `<version>${version}</version>`);
