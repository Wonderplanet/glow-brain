require 'rails_helper'

RSpec.describe "PuzzleSongTranslator" do
  subject { PuzzleSongTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opponents: 0)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleSongViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opponents).to eq use_case_data.opponents
    end
  end
end
