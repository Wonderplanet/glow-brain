using GLOW.Core.Domain.Repositories;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using WonderPlanet.AudioPlayableEngine;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Modules.GameOption.Domain.UseCases
{
    public class SwitchBgmGameOptionUseCase
    {
        [Inject] IAudioVolumeManagement AudioVolumeManagement { get; }
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }

        public BgmMuteFlag SwitchBgmGameOption()
        {
            var userPropertyModel = UserPropertyRepository.Get();
            var updatedUserPropertyModel = userPropertyModel with { IsBgmMute = !userPropertyModel.IsBgmMute };
            
            UserPropertyRepository.Save(updatedUserPropertyModel);
            
            AudioVolumeManagement.Mute(AudioChannel.BGM, updatedUserPropertyModel.IsBgmMute);
            
            return updatedUserPropertyModel.IsBgmMute;
        }
    }
}