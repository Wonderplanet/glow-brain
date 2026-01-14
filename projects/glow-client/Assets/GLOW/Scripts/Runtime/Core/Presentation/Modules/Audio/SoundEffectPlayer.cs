using System;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Modules.Audio
{
    public static class SoundEffectPlayer
    {
        public static void Play(SoundEffectId soundEffectId)
        {
            if (SoundEffects.Dictionary.TryGetValue(soundEffectId, out var soundEffect))
            {
                UISoundEffector.Main.Play(soundEffect.AssetKey.Value);
            }
        }

        public static void Play(SoundEffectAssetKey soundEffectAssetKey)
        {
            try
            {
                var id = (SoundEffectId)Enum.Parse(typeof(SoundEffectId), soundEffectAssetKey.Value);
                Play(id);
            }
            catch (Exception e)
            {
                ApplicationLog.LogError(
                    nameof(SoundEffectPlayer),
                    ZString.Format("Cannot Play Sound!! Key is {0}", soundEffectAssetKey.Value));
            }
        }

        public static void Stop(SoundEffectId soundEffectId)
        {
            if (SoundEffects.Dictionary.TryGetValue(soundEffectId, out var soundEffect))
            {
                UISoundEffector.Main.Stop(soundEffect.AssetKey.Value);
            }
        }

        public static void Stop()
        {
            UISoundEffector.Main.Stop();
        }
    }
}