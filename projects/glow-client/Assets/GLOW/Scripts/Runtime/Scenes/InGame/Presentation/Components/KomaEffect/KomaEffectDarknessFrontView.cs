using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaEffectDarknessFrontView : KomaEffectView
    {
        [SerializeField] AnimationPlayer _animationPlayer;
        [SerializeField] AnimationPlayer _clearAnimationPlayer;
        [SerializeField] CanvasGroup _canvasGroup;

        DarknessClearedFlag _cleared = DarknessClearedFlag.False;
        bool _isClearAnimationEnded;

        public override void UpdateKomaEffect(IKomaEffectModel komaEffectModel)
        {
            if (komaEffectModel.EffectType != KomaEffectType.Darkness) return;

            var darknessKomaEffectModel = komaEffectModel as DarknessKomaEffectModel;
            if (darknessKomaEffectModel == null) return;

            // 暗闇を晴らす
            if (darknessKomaEffectModel.Cleared == DarknessClearedFlag.True && _cleared == DarknessClearedFlag.False)
            {
                _cleared = DarknessClearedFlag.True;

                _clearAnimationPlayer.OnDone = () =>
                {
                    _isClearAnimationEnded = true;
                };

                _clearAnimationPlayer.Play();
                
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_074);
            }
        }

        public override void ResetKomaEffect(IKomaEffectModel komaEffectModel)
        {
            if (komaEffectModel.EffectType != KomaEffectType.Darkness) return;

            // 暗闇を復活
            _cleared = DarknessClearedFlag.False;
            _isClearAnimationEnded = false;
            _clearAnimationPlayer.Stop();
            _canvasGroup.alpha = 1;
        }

        public bool IsCleared()
        {
           return _cleared == DarknessClearedFlag.True && _isClearAnimationEnded;
        }

        public override MultipleSwitchHandler PauseWithoutDarknessClear(MultipleSwitchHandler handler)
        {
            // 暗闇が晴れるアニメーション中はポーズしない
            if (_cleared == DarknessClearedFlag.True && !_isClearAnimationEnded) return handler;

            return base.PauseWithoutDarknessClear(handler);
        }

        protected override void OnPause(bool pause)
        {
            if (_animationPlayer != null)
            {
                _animationPlayer.Pause(pause);
            }

            if (_clearAnimationPlayer != null)
            {
                _clearAnimationPlayer.Pause(pause);
            }
        }
    }
}
