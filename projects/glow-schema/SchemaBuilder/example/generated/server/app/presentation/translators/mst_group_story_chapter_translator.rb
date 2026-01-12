class MstGroupStoryChapterTranslator
  def self.translate(mst_group_story_chapter_model)
    view_model = MstGroupStoryChapterViewModel.new
    view_model.id = mst_group_story_chapter_model.id
    view_model.name = mst_group_story_chapter_model.name
    view_model
  end
end
