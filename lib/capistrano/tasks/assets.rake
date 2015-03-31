namespace :assets do
    desc <<-DESC
        Installs assets.
    DESC
    task :install do
        on roles(:app) do |host|
            within fetch(:release_path) do
               info "Installing the assets on #{host}"
               execute :php, "./bin/warez", "assets:install"
            end
        end
    end
end
