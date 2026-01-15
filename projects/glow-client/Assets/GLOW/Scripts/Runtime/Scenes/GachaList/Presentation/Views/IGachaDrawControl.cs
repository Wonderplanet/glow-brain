using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public interface IGachaDrawControl
    {
        void GachaDraw(
            MasterDataId oprGachaId,
            GachaType gachaType,
            GachaDrawCount drawCount,
            CostType costType,
            GachaDrawType gachaDrawType,
            CostAmount costAmount,
            MasterDataId costId,
            bool isReDraw);
        void UpdateView(MasterDataId drawOprGachaId);
        void DrawTutorialGacha(bool isReDraw);
    }
}
