namespace :grunt do
    desc <<-DESC
        Build the assets.
    DESC
    task :build do
        on roles(:app) do |host|
            within fetch(:release_path) do
               execute :grunt, "build"
               info "Building the assets on #{host}"
            end
        end
    end
end
