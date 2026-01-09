using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using WonderPlanet.AudioPlayableEngine;
using WPFramework.Constants.Asset;
using WPFramework.Constants.Zenject;
using WPFramework.Domain.Constants;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;
using Zenject;

namespace WPFramework.Domain.Modules
{
    public sealed class UnityAudioProcessor : IBackgroundMusicManagement, IBackgroundMusicPlayable, ISoundEffectManagement, ISoundEffectPlayable, IAudioVolumeManagement
    {
        [Inject(Id = FrameworkInjectId.AssetContainer.Audio)]
        IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }
        [Inject] IUnityAudioPlayableEngine UnityAudioPlayableEngine { get; }

        string _playingBgmAssetKey = default;

        async UniTask IBackgroundMusicManagement.Load(CancellationToken cancellationToken, string assetKey)
        {
            await AssetReferenceContainerRepository.Load<AudioTrackInfo>(
                cancellationToken,
                AssetReferenceContainerId.Audio.BackgroundMusicContainerId,
                AudioAssetPath.GetBGMPath(assetKey));
        }

        void IBackgroundMusicManagement.Unload(string assetKey)
        {
            var referenceContainer =
                AssetReferenceContainerRepository.Get<AudioTrackInfo>(
                    AssetReferenceContainerId.Audio.BackgroundMusicContainerId);
            referenceContainer?.Unload(AudioAssetPath.GetBGMPath(assetKey));
        }

        void IBackgroundMusicManagement.Unload()
        {
            var referenceContainer =
                AssetReferenceContainerRepository.Get<AudioTrackInfo>(
                    AssetReferenceContainerId.Audio.BackgroundMusicContainerId);
            referenceContainer?.Unload();
        }

        async UniTask ISoundEffectManagement.Load(CancellationToken cancellationToken, string[] assetKeys)
        {
            var tasks = Enumerable
                .Select(assetKeys, assetKey =>
                    AssetReferenceContainerRepository.Load<AudioTrackInfo>(
                        cancellationToken,
                        AssetReferenceContainerId.Audio.SoundEffectContainerId,
                        AudioAssetPath.GetSePath(assetKey)))
                .ToList();

            await UniTask.WhenAll(tasks);
        }

        void ISoundEffectManagement.Unload()
        {
            var referenceContainer =
                AssetReferenceContainerRepository.Get<AudioTrackInfo>(
                    AssetReferenceContainerId.Audio.SoundEffectContainerId);
            referenceContainer.Unload();
        }

        void ISoundEffectManagement.Unload(string assetKey)
        {
            var referenceContainer =
                AssetReferenceContainerRepository.Get<AudioTrackInfo>(
                    AssetReferenceContainerId.Audio.SoundEffectContainerId);
            referenceContainer?.Unload(AudioAssetPath.GetSePath(assetKey));
        }

        void ISoundEffectManagement.Unload(string[] assetKeys)
        {
            var referenceContainer =
                AssetReferenceContainerRepository.Get<AudioTrackInfo>(
                    AssetReferenceContainerId.Audio.SoundEffectContainerId);
            foreach (var assetKey in assetKeys)
            {
                referenceContainer?.Unload(AudioAssetPath.GetSePath(assetKey));
            }
        }

        void IBackgroundMusicPlayable.Play(string assetKey)
        {
            if (assetKey == _playingBgmAssetKey)
            {
                return;
            }

            var referenceContainer = AssetReferenceContainerRepository
                .Get<AudioTrackInfo>(AssetReferenceContainerId.Audio.BackgroundMusicContainerId);
            if (referenceContainer == null)
            {
                ApplicationLog.LogWarning(nameof(UnityAudioProcessor), $"Not loaded: {assetKey}");
                return;
            }
            var audioTrackInfo = referenceContainer.Get(AudioAssetPath.GetBGMPath(assetKey));
            UnityAudioPlayableEngine.Play(audioTrackInfo);
            _playingBgmAssetKey = assetKey;
        }

        async UniTask IBackgroundMusicPlayable.PlayWithCrossFade(CancellationToken cancellationToken, string assetKey, float duration)
        {
            if (assetKey == _playingBgmAssetKey)
            {
                return;
            }

            var referenceContainer = AssetReferenceContainerRepository
                .Get<AudioTrackInfo>(AssetReferenceContainerId.Audio.BackgroundMusicContainerId);
            if (referenceContainer == null)
            {
                ApplicationLog.LogWarning(nameof(UnityAudioProcessor), $"Not loaded: {assetKey}");
                return;
            }
            var audioTrackInfo = referenceContainer.Get(AudioAssetPath.GetBGMPath(assetKey));
            await UnityAudioPlayableEngine.PlayWithCrossFade(cancellationToken, audioTrackInfo, duration);
            _playingBgmAssetKey = assetKey;
        }

        async UniTask IBackgroundMusicPlayable.FadeIn(CancellationToken cancellationToken, float duration)
        {
            await UnityAudioPlayableEngine.FadeIn(cancellationToken, AudioChannel.BGM, duration);
        }

        async UniTask IBackgroundMusicPlayable.FadeOut(CancellationToken cancellationToken, float duration)
        {
            await UnityAudioPlayableEngine.FadeOut(cancellationToken, AudioChannel.BGM, duration);
        }

        void IBackgroundMusicPlayable.Stop()
        {
            UnityAudioPlayableEngine.Stop(AudioChannel.BGM);
            _playingBgmAssetKey = default;
        }

        void ISoundEffectPlayable.Play(string assetKey)
        {
            var referenceContainer =
                AssetReferenceContainerRepository
                    .Get<AudioTrackInfo>(AssetReferenceContainerId.Audio.SoundEffectContainerId);
            if (referenceContainer == null)
            {
                ApplicationLog.LogWarning(nameof(UnityAudioProcessor), $"Not loaded: {assetKey}");
                return;
            }
            var audioTrackInfo =
                referenceContainer.Get(AudioAssetPath.GetSePath(assetKey));
            UnityAudioPlayableEngine.PlayOneShot(audioTrackInfo);
        }
        
        void ISoundEffectPlayable.Stop(string assetKey)
        {
            var referenceContainer =
                AssetReferenceContainerRepository
                    .Get<AudioTrackInfo>(AssetReferenceContainerId.Audio.SoundEffectContainerId);
            if (referenceContainer == null)
            {
                ApplicationLog.LogWarning(nameof(UnityAudioProcessor), $"Not loaded: {assetKey}");
                return;
            }
            var audioTrackInfo =
                referenceContainer.Get(AudioAssetPath.GetSePath(assetKey));
            UnityAudioPlayableEngine.Stop(audioTrackInfo);
        }
        
        void ISoundEffectPlayable.Stop()
        {
            UnityAudioPlayableEngine.Stop(AudioChannel.SE);
        }

        void IAudioVolumeManagement.SetVolume(AudioChannel audioChannel, float volume)
        {
            UnityAudioPlayableEngine.SetVolume(audioChannel, volume);
        }

        float IAudioVolumeManagement.GetVolume(AudioChannel audioChannel)
        {
            return UnityAudioPlayableEngine.GetVolume(audioChannel);
        }

        void IAudioVolumeManagement.SetVolume(float volume)
        {
            UnityAudioPlayableEngine.SetVolume(volume);
        }

        float IAudioVolumeManagement.GetVolume()
        {
            return UnityAudioPlayableEngine.GetVolume();
        }

        void IAudioVolumeManagement.Mute(bool mute)
        {
            UnityAudioPlayableEngine.Mute(mute);
        }

        void IAudioVolumeManagement.Mute(AudioChannel audioChannel, bool mute)
        {
            UnityAudioPlayableEngine.Mute(audioChannel, mute);
        }

        bool IAudioVolumeManagement.IsMute(AudioChannel audioChannel)
        {
            return UnityAudioPlayableEngine.IsMute(audioChannel);
        }

        async UniTask IAudioVolumeManagement.Reset(CancellationToken cancellationToken)
        {
            await UnityAudioPlayableEngine.ResetVolume(cancellationToken);
        }
    }
}
