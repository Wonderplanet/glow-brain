using System;

namespace GLOW.Scenes.HomeMainKomaSettingFilter.Presentation
{
    public interface IHomeMainKomaSettingFilterViewDelegate
    {
        void OnViewDidLoad();
        void OnConfirm();
        void OnCancel();
    }
}
