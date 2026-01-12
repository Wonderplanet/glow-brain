using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class GachaBandComponent: UIObject
    {
        [SerializeField] UIImage _bandFestival;
        [SerializeField] UIImage _bandPickup;
        [SerializeField] UIImage _bandFree;
        [SerializeField] UIImage _bandTicket;
        [SerializeField] UIImage _bandPaidOnly;
        [SerializeField] UIText _festivalText;
        [SerializeField] UIText _pickupText;
        [SerializeField] UIText _freeText;
        [SerializeField] UIText _ticketText;
        [SerializeField] UIText _paidOnlyText;

        public void GachaContentBandSetup(GachaType type, GachaName gachaName)
        {
            _bandFestival.gameObject.SetActive(type == GachaType.Festival);
            _bandPickup.gameObject.SetActive(type == GachaType.Pickup);
            _bandFree.gameObject.SetActive(type == GachaType.Free);
            _bandTicket.gameObject.SetActive(type == GachaType.Ticket);
            _bandPaidOnly.gameObject.SetActive(type == GachaType.PaidOnly);

            switch (type)
            {
                case GachaType.Festival:
                    _festivalText.SetText(gachaName.Value);
                    break;
                case GachaType.Pickup:
                    _pickupText.SetText(gachaName.Value);
                    break;
                case GachaType.Free:
                    _freeText.SetText(gachaName.Value);
                    break;
                case GachaType.Ticket:
                    _ticketText.SetText(gachaName.Value);
                    break;
                case GachaType.PaidOnly:
                    _paidOnlyText.SetText(gachaName.Value);
                    break;

                default:
                    break;
            }
        }
    }
}
