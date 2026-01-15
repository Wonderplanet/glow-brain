using System.Collections.Generic;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;

namespace GLOW.Scenes.BoxGacha.Domain.Model
{
    public record BoxGachaDrawModel(
        BoxGachaInfoModel DrawnBoxGachaInfoModel,
        IReadOnlyList<BoxGachaDrawResultCellModel> DrawnBoxGachaResultRewards,
        IReadOnlyList<ArtworkFragmentAcquisitionModel> ArtworkFragmentAcquisitionModels)
    {
        public static BoxGachaDrawModel Empty { get; } = new(
            BoxGachaInfoModel.Empty,
            new List<BoxGachaDrawResultCellModel>(),
            new List<ArtworkFragmentAcquisitionModel>());
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}