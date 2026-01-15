require 'rails_helper'

RSpec.describe "MstMiniStoryCharacterTranslator" do
  subject { MstMiniStoryCharacterTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_character_id: 0, opr_mini_story_id: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstMiniStoryCharacterViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
      expect(view_model.opr_mini_story_id).to eq use_case_data.opr_mini_story_id
    end
  end
end
