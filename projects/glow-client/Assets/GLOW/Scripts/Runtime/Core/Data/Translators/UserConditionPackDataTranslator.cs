using System;
using System.Globalization;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UserConditionPackDataTranslator
    {
        public static UserConditionPackModel ToModel(UsrConditionPackData data)
        {
            return new UserConditionPackModel(
                new MasterDataId(data.MstPackId),
                DateTimeOffset.Parse(data.StartDate, CultureInfo.InvariantCulture));
        }
    }
}
