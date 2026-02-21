require 'rails_helper'

RSpec.describe "PuzzleResultDropSetTranslator" do
  subject { PuzzleResultDropSetTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, number: 0, drops: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleResultDropSetViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.number).to eq use_case_data.number
      expect(view_model.drops).to eq use_case_data.drops
    end
  end
end
