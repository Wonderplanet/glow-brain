using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Modules.Advertising;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using WonderPlanet.AudioPlayableEngine;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Core.Domain.UseCases
{
    public class InAppAdvertisingUseCase
    {
        [Inject] IAdvertisingPlayer AdvertisingPlayer { get; }
        [Inject] IAudioVolumeManagement AudioVolumeManagement { get; }
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }

        public async UniTask<GlowAdPlayRewardResultData> ShowAdAsync(
            IAARewardFeatureType iAARewardFeatureType,
            CancellationToken cancellationToken)
        {
            var propertyModel = UserPropertyRepository.Get();

            //広告とゲーム内音響が重複再生されるので、一時的にゲーム内音響をoffにする
            if (!propertyModel.IsBgmMute)
            {
                AudioVolumeManagement.Mute(AudioChannel.BGM, true);
            }

            if (!propertyModel.IsSeMute)
            {
                AudioVolumeManagement.Mute(AudioChannel.SE, true);
            }

            // 広告を表示
            // 本当はWireFrameで行うのが良いが、音響Muteの副作用と強く紐ついてしまっているのでここに表示の処理を入れている
            // (音響Muteと疎結合できる案が出れば修正)
            var result = await AdvertisingPlayer.ShowAdAsync(iAARewardFeatureType, cancellationToken);

            if (!propertyModel.IsBgmMute)
            {
                AudioVolumeManagement.Mute(AudioChannel.BGM, false);
            }

            if (!propertyModel.IsSeMute)
            {
                AudioVolumeManagement.Mute(AudioChannel.SE, false);
            }

            return result;
        }
    }
}
