using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class MstArtworkAcquisitionRouteDataTranslator
    {
        public static MstArtworkAcquisitionRouteModel Translate(
            string mstArtworkId,
            IReadOnlyList<MstArtworkAcquisitionRouteData> datas)
        {
            var acquisitionRoutes = datas
                .Select(data => new ArtworkAcquisitionRoute(
                    new MasterDataId(data.Id),
                    data.ContentType,
                    new MasterDataId(data.ContentId)))
                .ToList();

            return new MstArtworkAcquisitionRouteModel(new MasterDataId(mstArtworkId), acquisitionRoutes);
        }
    }
}
