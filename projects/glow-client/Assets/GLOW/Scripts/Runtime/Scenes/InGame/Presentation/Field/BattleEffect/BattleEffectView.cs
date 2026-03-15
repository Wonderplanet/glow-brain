using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BattleEffectView : BaseBattleEffectView
    {
        const string OutpostActivationTrackName = "OutpostActivation";
        const string UnitActivationTrackName = "UnitActivation";
        const string KomaShakeTrackName = "KomaShake";
        const string ScreenFlashTrackName = "ScreenFlash";

        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] AnimationPlayer _animationPlayer;
        [SerializeField] ParticleSystemComponent _particleSystemComponent;
        [SerializeField] string _attachTargetBone;
        [SerializeField] bool _followBoneRotation;

        public override void Destroy()
        {
            base.Destroy();
            Complete();
        }

        public override BaseBattleEffectView Play()
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.OnCompleted = Complete;
                _timelineAnimation.Play();
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.OnDone = Complete;
                _animationPlayer.Play();
            }
            else if (_particleSystemComponent != null)
            {
                _particleSystemComponent.OnStopped = Complete;
                _particleSystemComponent.Play();
            }

            return base.Play();
        }

        public override BaseBattleEffectView BindOutpost(GameObject bindRoot)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Bind(OutpostActivationTrackName, bindRoot);
            }

            return base.BindOutpost(bindRoot);
        }

        public override BaseBattleEffectView BindCharacterUnit(FieldUnitView fieldUnitView)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Bind(KomaShakeTrackName, fieldUnitView);
            }

            BindCharacterImage(fieldUnitView.UnitImage);

            return base.BindCharacterUnit(fieldUnitView);
        }

        public override BaseBattleEffectView BindSpecialUnit(FieldSpecialUnitView fieldSpecialUnitView)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Bind(KomaShakeTrackName, fieldSpecialUnitView);
            }

            BindCharacterImage(fieldSpecialUnitView.UnitImage);

            return base.BindSpecialUnit(fieldSpecialUnitView);
        }

        public override BaseBattleEffectView BindCharacterImage(UnitImage unitImage)
        {
            if (!string.IsNullOrEmpty(_attachTargetBone))
            {
                SkeletonAnimationFollowerFactory.BindSkeletonAnimation(
                    gameObject,
                    unitImage.SkeletonAnimation,
                    _attachTargetBone,
                    _followBoneRotation);
            }

            if (_timelineAnimation != null)
            {
                _timelineAnimation.Bind(UnitActivationTrackName, unitImage.gameObject);
            }

            return base.BindCharacterImage(unitImage);
        }

        public override BaseBattleEffectView BindScreenFlashDelegate(IScreenFlashTrackClipDelegate screenFlashDelegate)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Bind(ScreenFlashTrackName, screenFlashDelegate.GetObjectForBinding());
            }

            return base.BindScreenFlashDelegate(screenFlashDelegate);
        }

        public void OnSignalReceived(string parameter)
        {
            var signal = new BattleEffectSignal(parameter);
            InvokeSignalAction(signal);
        }

        /// <summary>
        /// エフェクトで使用されるSEのリストを取得する
        /// ※ Timelineの場合のみ対応
        /// </summary>
        /// <returns></returns>
        public List<SoundEffectId> GetSoundEffectIds()
        {
            if (_timelineAnimation != null)
            {
                return _timelineAnimation.GetSoundEffectIds();
            }

            return new List<SoundEffectId>();
        }

        protected override void OnPause(bool isPause)
        {
            base.OnPause(isPause);

            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(isPause);
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.Pause(isPause);
            }
            else if (_particleSystemComponent != null)
            {
                _particleSystemComponent.Pause(isPause);
            }
        }
    }
}
