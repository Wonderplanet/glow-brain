using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.GachaRatio.Presentation.Views.Components
{
    public class GachaRatioLineupListComponent : UIObject
    {
        [SerializeField] List<GachaRatioLineupComponent> _lineupList;

        public void Setup(GachaRatioLineupListViewModel viewModel)
        {
            // 表示物が一切無ければ非表示にする
            var lineupCount = viewModel.TotalCellCount;
            Hidden = lineupCount == 0;

            _lineupList[0].Setup(viewModel.URareLineupViewModel);
            _lineupList[1].Setup(viewModel.SSRareLineupViewModel);
            _lineupList[2].Setup(viewModel.SRareLineupViewModel);
            _lineupList[3].Setup(viewModel.RLineupViewModel);
        }
    }
}
