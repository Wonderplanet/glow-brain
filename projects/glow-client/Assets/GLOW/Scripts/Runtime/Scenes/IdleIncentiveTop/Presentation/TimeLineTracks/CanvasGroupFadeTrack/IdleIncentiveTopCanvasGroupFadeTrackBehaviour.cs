using UnityEngine;
using UnityEngine.Playables;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveTopCanvasGroupFadeTrackBehaviour : PlayableBehaviour
    {
        public AnimationCurve AnimationCurve { get; set; }

        CanvasGroup _canvasGroup;

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            var time = playable.GetTime();
            var canvasGroup = playerData as CanvasGroup;
            if (canvasGroup == null) return;
            _canvasGroup = canvasGroup;

            var duration = playable.GetDuration();
            canvasGroup.alpha = AnimationCurve.Evaluate((float)(time / duration));
        }

        public override void OnBehaviourPause(Playable playable, FrameData info)
        {
            base.OnBehaviourPause(playable, info);

            if (_canvasGroup == null) return;
            // フェードが終了したらalphaを最終フレームに設定する
            // FPSにより最終フレームまで到達しない場合があるため
            _canvasGroup.alpha = AnimationCurve.Evaluate(1.0f);
        }
    }
}
