namespace :npm do
    desc <<-DESC
        Installs the nodejs modules.
    DESC
    task :install_dependencies do
        on roles(:app) do |host|
            within fetch(:release_path) do
                execute :npm, "install"
                info "Installing npm modules on #{host}"
            end
        end
    end
end

