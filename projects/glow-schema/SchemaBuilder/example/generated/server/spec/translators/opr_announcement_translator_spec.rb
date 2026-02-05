require 'rails_helper'

RSpec.describe "OprAnnouncementTranslator" do
  subject { OprAnnouncementTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, priority: 1, tab_type: 2, label_type: 3, banner_path: 4, title: 5, start_at: 6, detail_path: 7, opr_event_id: 8)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprAnnouncementViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.priority).to eq use_case_data.priority
      expect(view_model.tab_type).to eq use_case_data.tab_type
      expect(view_model.label_type).to eq use_case_data.label_type
      expect(view_model.banner_path).to eq use_case_data.banner_path
      expect(view_model.title).to eq use_case_data.title
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.detail_path).to eq use_case_data.detail_path
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
    end
  end
end
