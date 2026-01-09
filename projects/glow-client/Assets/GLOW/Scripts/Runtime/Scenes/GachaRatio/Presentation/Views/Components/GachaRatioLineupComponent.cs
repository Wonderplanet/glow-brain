using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaRatio.Presentation.Views.Components
{
    public class GachaRatioLineupComponent : UIObject
    {
        [SerializeField] UIText _rarityAndNumber;
        [SerializeField] GachaRatioLineupCellComponent _cellPrefab;
        [SerializeField] Transform _contntRoot;

        List<GachaRatioLineupCellComponent> _lineupCellComponents = new();

        public void Setup(GachaRatioLineupViewModel viewModel)
        {
            // 排出ユニットが居ない場合表示しない
            Hidden = (viewModel.GashaRatioLineupCellViewModels.Count <= 0);
            if(Hidden) return;

            _rarityAndNumber.SetText(viewModel.RatioProbabilityAmount.ToString());

            // 排出確率の初期化
            foreach (var cell in _lineupCellComponents)
            {
                Destroy(cell.gameObject);
            }
            _lineupCellComponents.Clear();

            foreach (var cellViewModel in viewModel.GashaRatioLineupCellViewModels)
            {
                var lineupCell = Instantiate(_cellPrefab, _contntRoot);
                lineupCell.Setup(cellViewModel);

                _lineupCellComponents.Add(lineupCell);
            }
        }
    }
}
