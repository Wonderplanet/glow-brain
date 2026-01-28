using GLOW.Scenes.HomeMenuSetting.Domain.Model;
using GLOW.Scenes.HomeMenuSetting.Presentation.ViewModel;

namespace GLOW.Scenes.HomeMenuSetting.Presentation.Translator
{
    public class HomeMenuSettingViewModelTranslator
    {
        public static HomeMenuSettingViewModel ToHomeMenuSettingViewModel(HomeMenuSettingModel model)
        {
            return new HomeMenuSettingViewModel(
                model.IsBgmMute, 
                model.IsSeMute, 
                model.IsDamageDisplay,
                model.SpecialAttackCutInPlayType, 
                model.IsPushOff,
                model.AppVersion);
        }
    }
}