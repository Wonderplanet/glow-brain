using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaResult.Presentation.Views
{
    public class GachaResultRarityIconComponent : UIObject
    {
        [SerializeField] UIImage _rarirtyRIcon;
        [SerializeField] UIImage _rarirtySRIcon;
        [SerializeField] UIImage _rarirtySSRIcon;
        [SerializeField] UIImage _rarirtyURIcon;

        public void SetRarity(Rarity rarity)
        {
            _rarirtyRIcon.gameObject.SetActive(false);
            _rarirtySRIcon.gameObject.SetActive(false);
            _rarirtySSRIcon.gameObject.SetActive(false);
            _rarirtyURIcon.gameObject.SetActive(false);

            switch (rarity)
            {
                case Rarity.R:
                    _rarirtyRIcon.gameObject.SetActive(true);
                    break;
                case Rarity.SR:
                    _rarirtySRIcon.gameObject.SetActive(true);
                    break;
                case Rarity.SSR:
                    _rarirtySSRIcon.gameObject.SetActive(true);
                    break;
                case Rarity.UR:
                    _rarirtyURIcon.gameObject.SetActive(true);
                    break;
            }
        }
    }
}
