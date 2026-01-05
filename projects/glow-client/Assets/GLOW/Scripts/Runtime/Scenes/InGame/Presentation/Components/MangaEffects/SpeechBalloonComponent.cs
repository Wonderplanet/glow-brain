using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Components.MangaAnimation;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class SpeechBalloonComponent : AbstractMangaEffectComponent, IMangaAnimationSpeechBalloon
    {
        const float CutOffFlipThreshold = 130f; // 画面端からの距離がこれ未満のとき、吹き出しが見切れすぐないように逆側に出す

        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class SpeechBalloonInfo
        {
            public SpeechBalloonType Type;
            public SpeechBalloonSide Side;
            public List<SpeechBalloonPrefabInfo> PrefabInfos;
        }

        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class SpeechBalloonPrefabInfo
        {
            public SpeechBalloonElement Prefab;
            public int MaxTextLength;
        }

        [SerializeField] List<Transform> _roots;
        [SerializeField] List<SpeechBalloonInfo> _speechBalloonInfoList;

        MangaEffectLayer _mangaEffectLayer;
        SpeechBalloonElement _speechBalloonElement;
        SpeechBalloonText _speechBalloonText = SpeechBalloonText.Empty;
        bool _isAutoDestroy;
        float _pageWidth;
        int _rootIndex;

        public SpeechBalloonSide Side => _speechBalloonText.Side;

        public override void Setup(
            MangaEffectLayer mangaEffectLayer,
            FieldViewCoordV2 pos,
            bool isFlip,
            ICoordinateConverter coordinateConverter,
            IViewCoordinateConverter viewCoordinateConverter,
            float pageWidth)
        {
            base.Setup(mangaEffectLayer, pos, isFlip, coordinateConverter, viewCoordinateConverter, pageWidth);

            _mangaEffectLayer = mangaEffectLayer;
            _pageWidth = pageWidth;
            _rootIndex = 0;
        }

        public SpeechBalloonComponent Setup(SpeechBalloonText speechBalloonText)
        {
            // 画面左右の端で見切れる場合は吹き出し出すのを反対側にする
            _speechBalloonText = FlipIfCutOff(speechBalloonText);

            // 左側の吹き出しの場合は_rootをx軸で反転
            if (_speechBalloonText.Side == SpeechBalloonSide.Left)
            {
                FlipRoot();
            }

            var prefab = GetSpeechBalloon(_speechBalloonText);
            if (prefab == null) return null;

            var root = GetSpeechBalloonElementRoot();
            _speechBalloonElement = InstantiateMangaEffectElement(prefab, root);
            _speechBalloonElement.SetText(_speechBalloonText);

            // 同じキャラの同じ側に重複して吹き出しが出る場合はずらして出す
            SlideSpeechBalloons(_speechBalloonText);

            return this;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            OnCompleted?.Invoke();
        }

        public override void Destroy()
        {
            Destroy(gameObject);
        }

        public override AbstractMangaEffectComponent Play()
        {
            if (_speechBalloonElement == null) return this;

            _speechBalloonElement.Show();

            if (!_speechBalloonText.Duration.IsEmpty())
            {
                DelayHide(_speechBalloonText.Duration, this.GetCancellationTokenOnDestroy()).Forget();
            }

            return this;
        }

        /// <summary>
        /// 吹き出しの位置をキャラから一段階離す
        /// </summary>
        public void Slide()
        {
            ChangeRoot(_rootIndex + 1);
        }

        void ISpeechBalloonTrackSpeechBalloon.SetAnimationTime(SpeechBalloonAnimationTime time)
        {
            if (_speechBalloonElement == null) return;

            _speechBalloonElement.Seek(time);
        }

        void ISpeechBalloonTrackSpeechBalloon.EndSpeech()
        {
            Hide();
        }

        protected override void OnPause(bool pause)
        {
            if (_speechBalloonElement == null) return;

            _speechBalloonElement.Pause(pause);
        }

        async UniTask DelayHide(SpeechBalloonAnimationTime delay, CancellationToken cancellationToken)
        {
            float targetDuration = delay.Value;
            float start = Time.time;
            float pausedTime = 0f;
            float pauseStart = 0f;
            bool wasPaused = false;

            while (true)
            {
                await UniTask.Yield(PlayerLoopTiming.Update, cancellationToken);

                if (cancellationToken.IsCancellationRequested)
                {
                    return;
                }

                if (IsPaused())
                {
                    if (!wasPaused)
                    {
                        pauseStart = Time.time;
                        wasPaused = true;
                    }
                }
                else
                {
                    if (wasPaused)
                    {
                        pausedTime += Time.time - pauseStart;
                        wasPaused = false;
                    }

                    float elapsed = Time.time - start - pausedTime;
                    if (elapsed >= targetDuration)
                    {
                        break;
                    }
                }
            }

            Hide();
        }

        void Hide()
        {
            if (_speechBalloonElement == null) return;

            _speechBalloonElement.Hide(() => Destroy(gameObject));
        }

        SpeechBalloonElement GetSpeechBalloon(SpeechBalloonText speechBalloonText)
        {
            var speechBalloonInfo = _speechBalloonInfoList.Find(info =>
                info.Type == speechBalloonText.BalloonType && info.Side == speechBalloonText.Side);

            if (speechBalloonInfo == null) return null;

            int textLength = speechBalloonText.TextLength;

            var prefabInfo = speechBalloonInfo.PrefabInfos
                .Where(info => info.MaxTextLength >= textLength)
                .MinBy(info => info.MaxTextLength);

            if (prefabInfo == null) return null;

            return prefabInfo.Prefab;
        }

        void FlipRoot()
        {
            foreach (var root in _roots)
            {
                var rootPosition = root.localPosition;
                rootPosition.x *= -1;

                root.localPosition = rootPosition;
            }
        }

        SpeechBalloonText FlipIfCutOff(SpeechBalloonText speechBalloonText)
        {
            var side = speechBalloonText.Side;
            var posX = RectTransform.anchoredPosition.x;

            if (side == SpeechBalloonSide.Right && posX > -CutOffFlipThreshold)
            {
                side = SpeechBalloonSide.Left;
            }
            else if (side == SpeechBalloonSide.Left && posX < -(_pageWidth - CutOffFlipThreshold))
            {
                side = SpeechBalloonSide.Right;
            }

            return speechBalloonText with { Side = side };
        }

        void SlideSpeechBalloons(SpeechBalloonText speechBalloonText)
        {
            if (speechBalloonText.Side == SpeechBalloonSide.Left)
            {
                // キャラの左側に吹き出しを出す場合、他の吹き出しに被らないように、一番左に配置する
                var leftSpeechBalloonCount = _mangaEffectLayer.Effects
                    .OfType<SpeechBalloonComponent>()
                    .Where(balloon => balloon != this)
                    .Count(balloon => balloon.Side == SpeechBalloonSide.Left);

                ChangeRoot(leftSpeechBalloonCount);
            }
            else
            {
                // キャラの右側に吹き出しを出す場合、他の吹き出しをキャラから一段階離す
                var rightSpeechBalloons = _mangaEffectLayer.Effects
                    .OfType<SpeechBalloonComponent>()
                    .Where(balloon => balloon.Side == SpeechBalloonSide.Right)
                    .Where(balloon => balloon != this)
                    .ToList();

                foreach (var balloon in rightSpeechBalloons)
                {
                    balloon.Slide();
                }
            }
        }

        Transform GetSpeechBalloonElementRoot()
        {
            if (_roots.Count == 0) return null;

            var index = Mathf.Clamp(_rootIndex, 0, _roots.Count - 1);
            return _roots[index];
        }

        void ChangeRoot(int rootIndex)
        {
            _rootIndex = Mathf.Clamp(rootIndex, 0, _roots.Count - 1);

            var root = GetSpeechBalloonElementRoot();
            _speechBalloonElement.transform.SetParent(root, false);
        }
    }
}
