using System;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class WeakeningEffectView : BaseBattleEffectView
    {
        [SerializeField] AnimationPlayer _startPlayer;
        [SerializeField] AnimationPlayer _loopPlayer;

        Tween _colorChangeLoopTween;
        UnitImage _unitImage;
        
        public override void Destroy()
        {
            base.Destroy();

            _startPlayer.Stop();
            _loopPlayer.Stop();
            _colorChangeLoopTween?.Kill();

            Complete();
        }

        public override BaseBattleEffectView Play()
        {
            // スタート演出再生
            _startPlayer.Play();
            _startPlayer.OnDone = () =>
            {
                PlayLoop();
            };

            // キャラ色変化
            if (_unitImage != null)
            {
                Color32 baseColor = _unitImage.Color;
                Color32 afterBaseColor = new Color32(120, 120, 120, 255);
                DOTween.Sequence()
                    .Append(
                        DOTween
                            .To(() => _unitImage.Color, x => _unitImage.Color = x, afterBaseColor, 0.5f)
                            .SetEase(Ease.Linear))
                    .Append(
                        DOTween
                            .To(() => _unitImage.Color, x => _unitImage.Color = x, baseColor, 0.2f)
                            .SetEase(Ease.Linear).SetDelay(0.3f))
                    .OnComplete(() => _unitImage.Color = Color.white)
                    .Play();
            }

            return base.Play();
        }

        void PlayLoop()
        {
            // ループ演出再生
            _loopPlayer.gameObject.SetActive(true);
            _loopPlayer.Play();

            // キャラ色変化
            _colorChangeLoopTween?.Kill();
            _colorChangeLoopTween = DOTween.Sequence()
                .SetLoops(-1)
                .AppendInterval(2.0f)
                .AppendCallback(() =>
                {
                    if (_unitImage == null) return;

                    Color32 baseColor = _unitImage.Color;
                    Color32 afterBaseColor = new Color32(120, 120, 120, 255);
                    DOTween.Sequence()
                        .Append(
                            DOTween
                                .To(() => _unitImage.Color, x => _unitImage.Color = x, afterBaseColor, 0.5f)
                                .SetEase(Ease.Linear))
                        .Append(
                            DOTween
                                .To(() => _unitImage.Color, x => _unitImage.Color = x, baseColor, 0.2f)
                                .SetEase(Ease.Linear).SetDelay(0.3f))
                        .OnComplete(() => _unitImage.Color = Color.white)
                        .Play();
                })
                .AppendInterval(2.0f)
                .Play();
        }

        public override BaseBattleEffectView BindCharacterUnit(FieldUnitView fieldUnitView)
        {
            BindCharacterImage(fieldUnitView.UnitImage);

            return base.BindCharacterUnit(fieldUnitView);
        }

        public override BaseBattleEffectView BindCharacterImage(UnitImage unitImage)
        {
            _unitImage = unitImage;

            SkeletonAnimationFollowerFactory.BindSkeletonAnimation(
                gameObject,
                unitImage.SkeletonAnimation,
                "spine",
                false);

            return base.BindCharacterImage(unitImage);
        }

        protected override void OnPause(bool isPause)
        {
            _startPlayer.Pause(isPause);
            _loopPlayer.Pause(isPause);

            if (isPause)
            {
                _colorChangeLoopTween?.Pause();
            }
            else
            {
                _colorChangeLoopTween?.Play();
            }
        }
    }
}
