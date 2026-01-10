using System;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class FreezeEffectView : BaseBattleEffectView
    {
        const string UnitActivationTrackName = "UnitActivation";
        const string KomaShakeTrackName = "KomaShake";

        [SerializeField] TimelineAnimation _startTimeline; // 氷結状態付与アニメーション
        [SerializeField] TimelineAnimation _loopTimeline; // 氷結状態中のループアニメーション
        [SerializeField] TimelineAnimation _endTimeline; // 氷結状態解除のアニメーション

        public override void Destroy()
        {
            base.Destroy();

            _startTimeline.Stop();
            _loopTimeline.Stop();

            _endTimeline.OnCompleted = Complete;
            _endTimeline.Play();
        }

        public override BaseBattleEffectView Play()
        {
            _startTimeline.OnCompleted += () =>
            {
                _loopTimeline.Play();
            };
            _startTimeline.Play();

            return base.Play();
        }

        public override BaseBattleEffectView BindCharacterUnit(FieldUnitView fieldUnitView)
        {
            _startTimeline.Bind(KomaShakeTrackName, fieldUnitView);
            _loopTimeline.Bind(KomaShakeTrackName, fieldUnitView);
            _endTimeline.Bind(KomaShakeTrackName, fieldUnitView);

            BindCharacterImage(fieldUnitView.UnitImage);

            return base.BindCharacterUnit(fieldUnitView);
        }

        public override BaseBattleEffectView BindCharacterImage(UnitImage unitImage)
        {
            _startTimeline.Bind(UnitActivationTrackName, unitImage.gameObject);
            _loopTimeline.Bind(UnitActivationTrackName, unitImage.gameObject);
            _endTimeline.Bind(UnitActivationTrackName, unitImage.gameObject);

            return base.BindCharacterImage(unitImage);
        }

        protected override void OnPause(bool isPause)
        {
            base.OnPause(isPause);
            
            _startTimeline.Pause(isPause);
            _loopTimeline.Pause(isPause);
            _endTimeline.Pause(isPause);
        }
    }
}
