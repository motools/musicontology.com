task :submodule do |t|
  `git submodule init`
end

task :spec => [:submodule] do |t|
  `cd specification && php phpspecgen.php > index.html`
end

task :default => [:spec]
