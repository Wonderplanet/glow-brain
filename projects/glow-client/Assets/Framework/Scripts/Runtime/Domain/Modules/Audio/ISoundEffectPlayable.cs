namespace WPFramework.Domain.Modules
{
    public interface ISoundEffectPlayable
    {
        void Play(string assetKey);
        void Stop(string assetKey);
        void Stop();
    }
}
