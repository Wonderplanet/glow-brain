using GLOW.Core.Domain.Repositories;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using WonderPlanet.AudioPlayableEngine;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Modules.GameOption.Domain.UseCases
{
    public class SwitchSeGameOptionUseCase
    {
        [Inject] IAudioVolumeManagement AudioVolumeManagement { get; }
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }

        public SeMuteFlag SwitchSeGameOption()
        {
            var userPropertyModel = UserPropertyRepository.Get();
            var updatedUserPropertyModel = userPropertyModel with { IsSeMute = !userPropertyModel.IsSeMute };

            UserPropertyRepository.Save(updatedUserPropertyModel);
            
            AudioVolumeManagement.Mute(AudioChannel.SE, updatedUserPropertyModel.IsSeMute);

            return updatedUserPropertyModel.IsSeMute;
        }
    }
}