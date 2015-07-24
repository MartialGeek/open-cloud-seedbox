namespace :migrations do
    desc <<-DESC
        Run the SQL migrations.
    DESC
    task :migrate do
        on roles(:app) do |host|
            within fetch(:release_path) do
               info "Run the SQL migrations on #{host}"
               execute :php, "./bin/seedbox", "migrations:migrate"
            end
        end
    end
end
