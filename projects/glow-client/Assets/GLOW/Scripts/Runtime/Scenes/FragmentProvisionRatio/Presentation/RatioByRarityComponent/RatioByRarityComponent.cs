using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation.DestinationBanner
{
    [Serializable]
    public class RarioByRarityComponentObjectModel
    {
        public GameObject rootObject;
        public UIText ratioText;
    }
    public class RatioByRarityComponent : MonoBehaviour
    {
        [Header("レア度")]
        [SerializeField] RarioByRarityComponentObjectModel _rarityR;
        [SerializeField] RarioByRarityComponentObjectModel _raritySR;
        [SerializeField] RarioByRarityComponentObjectModel _raritySSR;
        [SerializeField] RarioByRarityComponentObjectModel _rarityUR;

        public RarioByRarityComponentObjectModel RarityR => _rarityR;
        public RarioByRarityComponentObjectModel RaritySR => _raritySR;
        public RarioByRarityComponentObjectModel RaritySSR => _raritySSR;
        public RarioByRarityComponentObjectModel RarityUR => _rarityUR;

        public void SetModel(Rarity rarity, OutputRatio ratio)
        {
            switch (rarity)
            {
                case Rarity.R:
                    _rarityR.ratioText.SetText(ratio.ToShowText());
                    _rarityR.rootObject.SetActive(!ratio.IsZero());
                    break;
                case Rarity.SR:
                    _raritySR.ratioText.SetText(ratio.ToShowText());
                    _raritySR.rootObject.SetActive(!ratio.IsZero());
                    break;
                case Rarity.SSR:
                    _raritySSR.ratioText.SetText(ratio.ToShowText());
                    _raritySSR.rootObject.SetActive(!ratio.IsZero());
                    break;
                case Rarity.UR:
                    _rarityUR.ratioText.SetText(ratio.ToShowText());
                    _rarityUR.rootObject.SetActive(!ratio.IsZero());
                    break;
            }
        }
    }
}
