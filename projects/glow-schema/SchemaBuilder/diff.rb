#!/usr/bin/ruby

require 'dotenv'
Dotenv.load

server_generated_dir = ENV["SERVER_GENERATED_DIR"]
server_repo_dir = ENV["SERVER_REPO_DIR"]

dirs = [
    ENV["VIEWMODEL_DIR"],
    ENV["CONTROLLER_DIR"],
    ENV["REQUEST_SPEC_DIR"],
    ENV["ROUTING_DIR"],
    ENV["TRANSLATOR_DIR"],
    ENV["TRANSLATOR_SPEC_DIR"],
    ENV["USE_CASE_DIR"],
    ENV["USE_CASE_SPEC_DIR"]
]

dirs.each do |dir|
    Dir.glob(server_generated_dir + dir + "/**/*.rb").each do |file|
        repo_path = file.gsub(server_generated_dir, server_repo_dir)
        p "diff #{file} #{repo_path}"
        system("diff #{file} #{repo_path}")
    end
end