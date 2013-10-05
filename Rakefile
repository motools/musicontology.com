task :submodule do |t|
  `git submodule init && git submodule update`
end

task :spec => [:submodule] do |t|
  `cd specification && php phpspecgen.php > index.html`
end

task :default => [:spec]
