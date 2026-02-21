using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public class VictoryResultSpeedAttackRewardList : UIObject
    {
        [SerializeField] VictoryResultSpeedAttackListCell _cellPrefab;
        [SerializeField] Transform _content;

        List<VictoryResultSpeedAttackListCell> _cells = new ();

        public void Setup(IReadOnlyList<ResultSpeedAttackRewardViewModel> list)
        {
            foreach(var cell in _cells)
            {
                Destroy(cell.gameObject);
            }
            _cells.Clear();

            foreach (var item in list)
            {
                var cell = Instantiate(_cellPrefab, _content);
                cell.Setup(item.RewardIcon, item.UpperClearTimeMs, item.IsAcquired, item.IsNew);
                _cells.Add(cell);
            }
        }

        public async UniTask PlayCellClearStampAsync(int index, CancellationToken cancellationToken)
        {
            if(index >= _cells.Count) return;
            await _cells[index].PlayClearStamp(cancellationToken);
        }

        public void SetClearStamp()
        {
            foreach(var cell in _cells)
            {
                cell.SetClearStamp();
            }
        }

        public float GetCellHeight()
        {
            return _cellPrefab.RectTransform.sizeDelta.y;
        }

        public float GetHeight()
        {
            var rectTransform = (RectTransform)transform;
            return rectTransform.sizeDelta.y;
        }
    }
}
