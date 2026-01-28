using System.Threading;
using Cysharp.Threading.Tasks;
using WonderPlanet.AudioPlayableEngine;

namespace WPFramework.Domain.Modules
{
    public interface IAudioVolumeManagement
    {
        void SetVolume(float volume);
        float GetVolume();
        void SetVolume(AudioChannel audioChannel, float volume);
        float GetVolume(AudioChannel audioChannel);
        void Mute(bool mute);
        void Mute(AudioChannel audioChannel, bool mute);
        bool IsMute(AudioChannel audioChannel);
        UniTask Reset(CancellationToken cancellationToken);
    }
}
