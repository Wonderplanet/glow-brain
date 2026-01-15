using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class GachaBandComponent: UIObject
    {
        [Header("ノーマル")]
        [SerializeField] UIImage _bandNormal;
        [SerializeField] UIText _bandNormalText;
        [Header("プレミアム")]
        [SerializeField] UIImage _bandPremium;
        [SerializeField] UIText _bandPremiumText;
        [Header("ピックアップ")]
        [SerializeField] UIImage _bandPickup;
        [SerializeField] UIText _pickupText;
        [Header("無料")]
        [SerializeField] UIImage _bandFree;
        [SerializeField] UIText _freeText;
        [Header("フェス")]
        [SerializeField] UIImage _bandFestival;
        [SerializeField] UIText _festivalText;
        [Header("チケット")]
        [SerializeField] UIImage _bandTicket;
        [SerializeField] UIText _ticketText;
        [Header("有料限定")]
        [SerializeField] UIImage _bandPaidOnly;
        [SerializeField] UIText _paidOnlyText;
        [Header("メダル")]
        [SerializeField] UIImage _bandMedal;
        [SerializeField] UIText _bandMedalText;
        [Header("チュートリアル")]
        [SerializeField] UIImage _bandTutorial;
        [SerializeField] UIText _bandTutorialText;

        public void GachaContentBandSetup(GachaType type, GachaName gachaName)
        {
            _bandNormal.gameObject.SetActive(type == GachaType.Normal);
            _bandPremium.gameObject.SetActive(type == GachaType.Premium);
            _bandPickup.gameObject.SetActive(type == GachaType.Pickup);
            _bandFree.gameObject.SetActive(type == GachaType.Free);
            _bandFestival.gameObject.SetActive(type == GachaType.Festival);
            _bandTicket.gameObject.SetActive(type == GachaType.Ticket);
            _bandPaidOnly.gameObject.SetActive(type == GachaType.PaidOnly);
            _bandMedal.gameObject.SetActive(type == GachaType.Medal);
            _bandTutorial.gameObject.SetActive(type == GachaType.Tutorial);

            switch (type)
            {
                case GachaType.Normal:
                    _bandNormalText.SetText(gachaName.Value);
                    break;
                case GachaType.Premium:
                    _bandPremiumText.SetText(gachaName.Value);
                    break;
                case GachaType.Pickup:
                    _pickupText.SetText(gachaName.Value);
                    break;
                case GachaType.Free:
                    _freeText.SetText(gachaName.Value);
                    break;
                case GachaType.Festival:
                    _festivalText.SetText(gachaName.Value);
                    break;
                case GachaType.Ticket:
                    _ticketText.SetText(gachaName.Value);
                    break;
                case GachaType.PaidOnly:
                    _paidOnlyText.SetText(gachaName.Value);
                    break;
                case GachaType.Medal:
                    _bandMedalText.SetText(gachaName.Value);
                    break;
                case GachaType.Tutorial:
                    _bandTutorialText.SetText(gachaName.Value);
                    break;
                default:
                    break;
            }
        }
    }
}
