using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components.MangaAnimation
{
    [RequireComponent(typeof(TimelineAnimation))]
    public class MangaAnimation : UIObject,
        ISpeechBalloonTrackClipDelegate,
        IKomaScrollTrackClipDelegate,
        IKomaZoomTrackClipDelegate
    {
        IMangaAnimationTrackDelegate _trackDelegate;
        TimelineAnimation _timelineAnimation;

        protected override void Awake()
        {
            base.Awake();

            _timelineAnimation = GetComponent<TimelineAnimation>();
        }
        
        public void Initialize(IMangaAnimationTrackDelegate trackDelegate)
        {
            _trackDelegate = trackDelegate;
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            if (_timelineAnimation != null)
            {
                await _timelineAnimation.PlayAsync(cancellationToken);
            }
        }

        public void Pause(bool pause)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(pause);
            }
        }

        ISpeechBalloonTrackSpeechBalloon ISpeechBalloonTrackClipDelegate.GenerateSpeechBalloon(
            AutoPlayerSequenceElementId speaker,
            SpeechBalloonText text,
            SpeechBalloonAnimationTime timeOffset)
        {
            return _trackDelegate.GenerateSpeechBalloon(speaker, text, timeOffset);
        }

        float IKomaScrollTrackClipDelegate.GetCurrentKomaScrollPosition()
        {
            return _trackDelegate.GetCurrentKomaScrollPosition();
        }

        float IKomaScrollTrackClipDelegate.GetKomaScrollPosition(AutoPlayerSequenceElementId target)
        {
            return _trackDelegate.GetKomaScrollPosition(target);
        }

        void IKomaScrollTrackClipDelegate.SetKomaScrollPosition(float position)
        {
            _trackDelegate.SetKomaScrollPosition(position);
        }

        KomaId IKomaZoomTrackClipDelegate.GetKomaId(AutoPlayerSequenceElementId target)
        {
            return _trackDelegate.GetKomaId(target);
        }

        void IKomaZoomTrackClipDelegate.SetKomaZoomRate(KomaId komaId, AutoPlayerSequenceElementId target, float zoomRate)
        {
            _trackDelegate.SetKomaZoomRate(komaId, target, zoomRate);
        }
    }
}
