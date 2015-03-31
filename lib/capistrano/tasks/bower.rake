namespace :bower do
    desc <<-DESC
        Installs the bower dependencies.
    DESC
    task :install_dependencies do
        on roles(:app) do |host|
            within fetch(:release_path) do
               info "Installing bower dependencies on #{host}"
               execute :bower, "install"
            end
        end
    end
end
