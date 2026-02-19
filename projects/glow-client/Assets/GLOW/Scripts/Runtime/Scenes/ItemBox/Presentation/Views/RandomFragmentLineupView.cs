using System.Collections.Generic;
using GLOW.Scenes.ItemBox.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public class RandomFragmentLineupView : UIView
    {
        [SerializeField] RandomFragmentLineupList _lineupList;
        
        public void Setup(IReadOnlyList<LineupFragmentViewModel> lineup)
        {
            _lineupList.Setup(lineup);
        }
    }
}
