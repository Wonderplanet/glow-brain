using System;
using Cysharp.Text;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public class VictoryResultSpeedAttackComponent : UIObject
    {
        [SerializeField] GameObject _newRecord;
        [SerializeField] UIText _recordTimeText;

        public void Setup(long clearTimeMs)
        {
            var timeSpan = TimeSpan.FromMilliseconds(clearTimeMs);
            var text = ZString.Format("{0:D3}.{1:D2}", (int)timeSpan.TotalSeconds, timeSpan.Milliseconds/10);
            _recordTimeText.SetText(text);
        }

        public void HiddenNewRecord()
        {
            _newRecord.SetActive(false);
        }

        public void ShowNewRecord()
        {
            _newRecord.SetActive(true);
            _newRecord.transform.localScale = Vector3.zero;
            _newRecord.transform.DOScale(Vector3.one, 0.2f)
                .SetEase(Ease.OutBack);
        }
    }
}
