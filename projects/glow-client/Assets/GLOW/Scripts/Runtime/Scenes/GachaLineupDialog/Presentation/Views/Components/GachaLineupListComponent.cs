using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Views.Components
{
    public class GachaLineupListComponent : UIObject
    {
        [SerializeField] List<GachaLineupComponent> _lineupList;

        public void Setup(GachaLineupListViewModel viewModel)
        {
            // 表示物が一切無ければ非表示にする
            var lineupCount = viewModel.TotalCellCount;
            Hidden = lineupCount == 0;

            _lineupList[0].Setup(viewModel.URareLineupCellListViewModel);
            _lineupList[1].Setup(viewModel.SSRareLineupCellListViewModel);
            _lineupList[2].Setup(viewModel.SRareLineupCellListViewModel);
            _lineupList[3].Setup(viewModel.RLineupCellListViewModel);
        }
    }
}
