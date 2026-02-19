using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Views.Components
{
    public class GachaLineupComponent : UIObject
    {
        [SerializeField] UIText _rarityAndNumber;
        [SerializeField] GachaLineupCellComponent _cellPrefab;
        [SerializeField] Transform _contentRoot;

        List<GachaLineupCellComponent> _lineupCellComponents = new();

        public void Setup(GachaLineupCellListViewModel viewModel)
        {
            // 排出ユニットが居ない場合表示しない
            Hidden = (viewModel.GachaLineupCellViewModels.Count <= 0);
            if(Hidden) return;

            _rarityAndNumber.SetText(viewModel.RatioProbabilityAmount.ToString());

            // 排出確率の初期化
            foreach (var cell in _lineupCellComponents)
            {
                Destroy(cell.gameObject);
            }
            _lineupCellComponents.Clear();

            foreach (var cellViewModel in viewModel.GachaLineupCellViewModels)
            {
                var lineupCell = Instantiate(_cellPrefab, _contentRoot);
                lineupCell.Setup(cellViewModel);

                _lineupCellComponents.Add(lineupCell);
            }
        }
    }
}
