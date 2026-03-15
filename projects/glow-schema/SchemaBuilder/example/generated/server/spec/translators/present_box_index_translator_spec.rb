require 'rails_helper'

RSpec.describe "PresentBoxIndexTranslator" do
  subject { PresentBoxIndexTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, present_boxes: 0, present_box_histories: 1, deleted_count: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(PresentBoxIndexViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.present_boxes).to eq use_case_data.present_boxes
      expect(view_model.present_box_histories).to eq use_case_data.present_box_histories
      expect(view_model.deleted_count).to eq use_case_data.deleted_count
    end
  end
end
