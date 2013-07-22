task :submodule do |t|
  `git submodule init`
end

task :spec => [:submodule] do |t|
  `cd docs && php phpspecgen.php > specification.html`
end

task :default => [:spec]
