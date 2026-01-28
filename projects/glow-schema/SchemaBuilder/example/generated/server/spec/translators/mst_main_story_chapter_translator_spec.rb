require 'rails_helper'

RSpec.describe "MstMainStoryChapterTranslator" do
  subject { MstMainStoryChapterTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, number: 1, name: 2, release_at: 3, publication_at: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstMainStoryChapterViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.number).to eq use_case_data.number
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.release_at).to eq use_case_data.release_at
      expect(view_model.publication_at).to eq use_case_data.publication_at
    end
  end
end
