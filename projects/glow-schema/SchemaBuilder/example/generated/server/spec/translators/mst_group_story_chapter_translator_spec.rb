require 'rails_helper'

RSpec.describe "MstGroupStoryChapterTranslator" do
  subject { MstGroupStoryChapterTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstGroupStoryChapterViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
    end
  end
end
