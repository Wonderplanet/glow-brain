using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.HomeMenuSetting.Domain.Model;
using GLOW.Scenes.Title.Domains.ValueObjects;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.HomeMenuSetting.Domain.UseCase
{
    public class ShowHomeMenuSettingUseCase
    {
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        
        public HomeMenuSettingModel GetHomeMenuSetting()
        {
            var userProperty = UserPropertyRepository.Get();
            var systemInfo = SystemInfoProvider.GetApplicationSystemInfo();

            return new HomeMenuSettingModel(
                userProperty.IsBgmMute, 
                userProperty.IsSeMute, 
                userProperty.IsDamageDisplay,
                userProperty.SpecialAttackCutInPlayType, 
                userProperty.IsPushOff,
                new ApplicationVersion(systemInfo.Version));
        }
    }
}