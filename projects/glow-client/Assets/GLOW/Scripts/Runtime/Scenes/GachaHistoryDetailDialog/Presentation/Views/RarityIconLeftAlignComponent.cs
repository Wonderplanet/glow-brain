using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Presentation.Views
{
    public class RarityIconLeftAlignComponent : UIObject
    {
        [SerializeField] UIObject _rIcon;
        [SerializeField] UIObject _srIcon;
        [SerializeField] UIObject _ssrIcon;
        [SerializeField] UIObject _urIcon;
        
        public void Setup(Rarity rarity)
        {
            _rIcon.Hidden = rarity != Rarity.R;
            _srIcon.Hidden = rarity != Rarity.SR;
            _ssrIcon.Hidden = rarity != Rarity.SSR;
            _urIcon.Hidden = rarity != Rarity.UR;
        }
    }
}