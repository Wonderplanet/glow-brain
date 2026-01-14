using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Modules.TimeScaleController;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.Field;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components.MangaAnimation
{
    public class MangaAnimationPlayer : UIObject, IMangaAnimationTrackDelegate
    {
        [SerializeField] SpeechBalloonComponent _speechBalloonPrefab;

        IMangaAnimationContainer _mangaAnimationContainer;
        PageComponent _pageComponent;
        BattleFieldView _battleFieldView;
        PrefabFactory<MangaAnimation> _mangaAnimationFactory;
        ITimeScaleController _timeScaleController;

        readonly List<MangaAnimation> _mangaAnimations = new ();
        readonly MultipleSwitchController _pauseController = new ();
        ITimeScaleControlHandler _timeScaleHandler;

        protected override void Awake()
        {
            base.Awake();

            _pauseController.OnStateChanged = OnPause;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();

            _pauseController.Dispose();
            _timeScaleHandler?.Dispose();
        }
        
        public void Initialize(
            IMangaAnimationContainer mangaAnimationContainer,
            PageComponent pageComponent,
            BattleFieldView battleFieldView,
            PrefabFactory<MangaAnimation> mangaAnimationFactory,
            ITimeScaleController timeScaleController)
        {
            _mangaAnimationContainer = mangaAnimationContainer;
            _pageComponent = pageComponent;
            _battleFieldView = battleFieldView;
            _mangaAnimationFactory = mangaAnimationFactory;
            _timeScaleController = timeScaleController;
        }

        public async UniTask Play(
            MangaAnimationAssetKey assetKey, 
            MangaAnimationSpeed animationSpeed, 
            CancellationToken cancellationToken)
        {
            MangaAnimation mangaAnimation = null;

            try
            {
                var prefab = _mangaAnimationContainer.Get(assetKey);
                if (prefab == null) return;

                mangaAnimation = _mangaAnimationFactory.Create(prefab);
                if (mangaAnimation == null) return;

                _mangaAnimations.Add(mangaAnimation);

                mangaAnimation.transform.SetParent(transform, false);

                mangaAnimation.Initialize(this);
                
                // 原画演出開始時にTimeScaleをAnimationSpeedに設定
                // Fixed型で高優先度を使用して確実に設定する
                _timeScaleHandler?.Dispose();
                _timeScaleHandler = null;
                
                if (_timeScaleController != null && !animationSpeed.IsEmpty())
                {
                    _timeScaleHandler = _timeScaleController.ChangeTimeScale(
                        animationSpeed.Value, 
                        TimeScaleType.Fixed, 
                        TimeScalePriorityDefinitions.MangaAnimation);
                }
                
                await mangaAnimation.PlayAsync(cancellationToken);

                DestroyMangaAnimation(mangaAnimation);
            }
            catch (OperationCanceledException)
            {
                DestroyMangaAnimation(mangaAnimation);
                SoundEffectPlayer.Stop();
                throw;
            }
            finally
            {
                // 原画演出終了時にTimeScaleを元に戻す
                _timeScaleHandler?.Dispose();
                _timeScaleHandler = null;
            }
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        IMangaAnimationSpeechBalloon IMangaAnimationTrackDelegate.GenerateSpeechBalloon(
            AutoPlayerSequenceElementId speaker,
            SpeechBalloonText text,
            SpeechBalloonAnimationTime timeOffset)
        {
            var unitView = _battleFieldView.FindUnitView(speaker);
            if (unitView == null) return null;

            var effect = _pageComponent
                .GenerateMangaEffect(_speechBalloonPrefab, unitView, false)
                ?.Setup(text)
                ?.Play();

            var speechBalloon = effect as IMangaAnimationSpeechBalloon;
            if (speechBalloon != null)
            {
                speechBalloon.SetAnimationTime(timeOffset);
            }

            return speechBalloon;
        }

        float IMangaAnimationTrackDelegate.GetCurrentKomaScrollPosition()
        {
            return _pageComponent.GetCurrentScrollPosition();
        }

        float IMangaAnimationTrackDelegate.GetKomaScrollPosition(AutoPlayerSequenceElementId target)
        {
            var unitView = _battleFieldView.FindUnitView(target);
            if (unitView == null) return 0f;

            return _pageComponent.GetScrollPosition(unitView.FieldViewPos, false);
        }

        void IMangaAnimationTrackDelegate.SetKomaScrollPosition(float position)
        {
            _pageComponent.SetScrollPosition(position);
        }

        KomaId IMangaAnimationTrackDelegate.GetKomaId(AutoPlayerSequenceElementId target)
        {
            var unitView = _battleFieldView.FindUnitView(target);
            if (unitView == null) return KomaId.Empty;

            return _pageComponent.GetKomaId(unitView.FieldViewPos);
        }

        void IMangaAnimationTrackDelegate.SetKomaZoomRate(KomaId komaId, AutoPlayerSequenceElementId target, float zoomRate)
        {
            var unitView = _battleFieldView.FindUnitView(target);
            if (unitView == null) return;

            _pageComponent.ScalePageTo(unitView.TrackingFieldViewPos, zoomRate);
        }

        void OnPause(bool isPause)
        {
            foreach (var mangaAnimation in _mangaAnimations)
            {
                mangaAnimation.Pause(isPause);
            }
        }

        void DestroyMangaAnimation(MangaAnimation mangaAnimation)
        {
            if (mangaAnimation == null) return;

            _mangaAnimations.Remove(mangaAnimation);
            Destroy(mangaAnimation.gameObject);
        }
    }
}
