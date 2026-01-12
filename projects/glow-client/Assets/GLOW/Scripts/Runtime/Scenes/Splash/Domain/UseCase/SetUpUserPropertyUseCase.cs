using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using WPFramework.Domain.Modules;
using WonderPlanet.AudioPlayableEngine;
using Zenject;

namespace GLOW.Scenes.Splash.Domain.UseCase
{
    public sealed class SetUpUserPropertyUseCase
    {
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }
        [Inject] IAudioVolumeManagement AudioVolumeManagement { get; }

        public async UniTask SetUp(CancellationToken cancellationToken)
        {
            await LoadUserProperty(cancellationToken);
            
            SetAudioProperty();
        }

        async UniTask LoadUserProperty(CancellationToken cancellationToken)
        {
            await UserPropertyRepository.Load(cancellationToken);
        }

        void SetAudioProperty()
        {
            var userPropertyModel = UserPropertyRepository.Get();
            AudioVolumeManagement.Mute(AudioChannel.BGM, userPropertyModel.IsBgmMute);
            AudioVolumeManagement.Mute(AudioChannel.SE, userPropertyModel.IsSeMute);
        }
    }
}
