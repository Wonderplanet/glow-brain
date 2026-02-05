using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class SpeechBalloonElement : AnimationMangaEffectElement
    {
        [SerializeField] AnimationClip _hideAnimationClip;
        [SerializeField] List<VerticalText> _lineTextList;
        [SerializeField] int _maxTextLengthOfLine;

        public void SetText(SpeechBalloonText text)
        {
            if (_maxTextLengthOfLine <= 0) return;

            var splitTexts = SplitText(text.Text);

            for (var i = 0; i < _lineTextList.Count; i++)
            {
                var lineText = _lineTextList[i];

                if (i < splitTexts.Count)
                {
                    lineText.SetText(splitTexts[i]);
                }
                else
                {
                    lineText.SetText(string.Empty);
                }
            }
        }

        public void Show()
        {
            AnimationPlayer.Play();
        }

        public void Hide(Action onComplete)
        {
            AnimationPlayer.OnDone = onComplete;
            AnimationPlayer.AnimationClip = _hideAnimationClip;

            AnimationPlayer.Play();
        }

        public void Seek(SpeechBalloonAnimationTime time)
        {
            var animationPlayerTime = new AnimationPlayerTime(time.Value);
            AnimationPlayer.Seek(animationPlayerTime);
        }

        public void Pause(bool pause)
        {
            AnimationPlayer.Pause(pause);
        }

        /// <summary>
        /// テキストを1行ごとに分割する
        /// </summary>
        List<string> SplitText(string text)
        {
            // まず改行コードで分割
            var splitTextsByLineBreak = text.Split("\n");

            // さらに1行の最大文字数で分割
            var splitTexts = new List<string>();
            foreach (string splitTextByLineBreak in splitTextsByLineBreak)
            {
                var lineCount = Mathf.CeilToInt(splitTextByLineBreak.Length / (float)_maxTextLengthOfLine);
                for (var i = 0; i < lineCount; i++)
                {
                    var startIndex = i * _maxTextLengthOfLine;
                    var length = Mathf.Min(_maxTextLengthOfLine, splitTextByLineBreak.Length - startIndex);
                    splitTexts.Add(splitTextByLineBreak.Substring(startIndex, length));
                }
            }

            return splitTexts;
        }
    }
}
