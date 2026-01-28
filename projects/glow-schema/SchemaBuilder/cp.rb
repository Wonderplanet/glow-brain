#!/usr/bin/ruby

require 'dotenv'
Dotenv.load

server_generated_dir = ENV["SERVER_GENERATED_DIR"]
server_repo_dir = ENV["SERVER_REPO_DIR"]
AUTO_GENERATED_DIR = "/auto_generated/"

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
    rmtarget = server_repo_dir + dir + AUTO_GENERATED_DIR + "*.rb"
    p "rm #{rmtarget}"
    FileUtils.rm(Dir.glob(rmtarget))
    Dir.glob(server_generated_dir + dir + "/**/*.rb").each do |file|
        repo_path = file.gsub(server_generated_dir, server_repo_dir)
        p repo_path
        auto_generated_path = file.gsub(server_generated_dir, server_repo_dir).gsub(dir, dir + AUTO_GENERATED_DIR)
        p auto_generated_path
        if (!File.exist?(repo_path))
            p "cp #{file} #{auto_generated_path}"
            FileUtils.cp(file, auto_generated_path)
        end
    end
end