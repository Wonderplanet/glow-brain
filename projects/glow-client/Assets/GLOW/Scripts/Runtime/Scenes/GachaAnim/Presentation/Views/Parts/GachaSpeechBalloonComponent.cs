using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Parts
{
    public class GachaSpeechBalloonComponent : UIObject
    {
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

        [SerializeField] Transform _root;
        [SerializeField] List<SpeechBalloonInfo> _speechBalloonInfoList;

        SpeechBalloonElement _speechBalloonElement;
        SpeechBalloonText _speechBalloonText = SpeechBalloonText.Empty;


        public void Setup(SpeechBalloonText speechBalloonText)
        {
            if(_speechBalloonElement != null)
            {
                Destroy(_speechBalloonElement.gameObject);
            }

            // 吹き出しが反対の場合_rootをx軸で反転
            if (_speechBalloonText.Side != speechBalloonText.Side)
            {
                FlipRoot();
            }
            _speechBalloonText = speechBalloonText;

            var prefab = GetSpeechBalloon(_speechBalloonText);
            if (prefab == null) return;

            _speechBalloonElement = Instantiate(prefab, _root);
            _speechBalloonElement.SetText(_speechBalloonText);
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

        public void Play()
        {
            if (_speechBalloonElement == null) return;
            _speechBalloonElement.RectTransform.localScale = Vector3.one;
        }

        void FlipRoot()
        {
            var rootPosition = _root.localPosition;
            rootPosition.x *= -1;

            _root.localPosition = rootPosition;
        }
    }
}
