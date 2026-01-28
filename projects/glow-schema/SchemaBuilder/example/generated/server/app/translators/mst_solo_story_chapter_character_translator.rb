class MstSoloStoryChapterCharacterTranslator
  def self.translate(mst_solo_story_chapter_character_model)
    view_model = MstSoloStoryChapterCharacterViewModel.new
    view_model.id = mst_solo_story_chapter_character_model.id
    view_model.mst_solo_story_chapter_id = mst_solo_story_chapter_character_model.mst_solo_story_chapter_id
    view_model.mst_character_id = mst_solo_story_chapter_character_model.mst_character_id
    view_model
  end
end
