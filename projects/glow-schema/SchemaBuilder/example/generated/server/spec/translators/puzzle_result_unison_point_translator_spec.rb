require 'rails_helper'

RSpec.describe "PuzzleResultUnisonPointTranslator" do
  subject { PuzzleResultUnisonPointTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_character_id: 0, before_point: 1, after_point: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleResultUnisonPointViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_character_id).to eq use_case_data.mst_character_id
      expect(view_model.before_point).to eq use_case_data.before_point
      expect(view_model.after_point).to eq use_case_data.after_point
    end
  end
end
