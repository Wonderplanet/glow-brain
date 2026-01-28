require 'rails_helper'

RSpec.describe "MiniStoryTranslator" do
  subject { MiniStoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_mini_story_id: 0, play_count: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(MiniStoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_mini_story_id).to eq use_case_data.opr_mini_story_id
      expect(view_model.play_count).to eq use_case_data.play_count
    end
  end
end
