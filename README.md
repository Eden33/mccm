## MCCM Project

MCCM Homepage based on Wordpress, custom theme and some plugins: http://www.mccm-feldkirch.at/

The code was maintained in SVN from 2012 to 2018 and finally transferred to Git in 2018.
You can find an article about it here: https://www.codingcookie.com/local-git-svn-projects-to-github/

## Development

1. docker compose up
2. Copy manually:
- `wp-content\themes\twentyeleven` to `wordpress\wp-content\themes\twentyeleven`
- `wp-content\themes\mccm` to `wordpress\wp-content\themes\mccm`
- `wp-content\uploads\` to `wordpress\wp-content\uploads\`
- `wp-content\plugins\` to `wordpress\wp-content\plugins\`
3. Take mysql dump from live website that includes `drop table procedure in case exists` in the dump file.
4. In the dump file:
- replace www.mccm-feldkirch.at with localhost:8080
- replace https with http
5. Import modified dump file http://localhost:8180/
6. Open website: http://localhost:8080
7. Active the plugins you want to include into testing: http://localhost:8080\wp-admin
