using System.Collections.Generic;

namespace GLOW.Debugs.AdminDebug.Domain.Models
{
    public record AdminDebugParameterDefinitionModel(
        string Type,
        int? Min,
        int? Max,
        string Description)
    {
        public string Type { get; } = Type;
        public int? Min { get; } = Min;
        public int? Max { get; } = Max;
        public string Description { get; } = Description;
    }
    public record AdminDebugMenuCommandModel(
        string Command,
        string Name,
        string Description,
        Dictionary<string, AdminDebugParameterDefinitionModel> RequiredParameters = null)
    {
        public string Command { get; } = Command;
        public string Name { get; } = Name;
        public string Description { get; } = Description;
        public Dictionary<string, AdminDebugParameterDefinitionModel> RequiredParameters { get; } = RequiredParameters;
        public bool IsParameterized => RequiredParameters is { Count: > 0 };
    }
    public record AdminDebugMenuCommandListModel(AdminDebugMenuCommandModel[] DebugMenuCommandModels)
    {
        public AdminDebugMenuCommandModel[] DebugMenuCommandModels { get; } = DebugMenuCommandModels;
    }
}
